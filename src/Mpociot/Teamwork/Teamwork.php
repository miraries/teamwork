<?php namespace Mpociot\Teamwork;

use Auth;

/**
 * This file is part of Teamwork
 *
 * @license MIT
 * @package Teamwork
 */

class Teamwork
{
    /**
     * Laravel application
     *
     * @var \Illuminate\Foundation\Application
     */
    public $app;

    /**
     * Create a new Teamwork instance.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct( $app )
    {
        $this->app = $app;
    }

    /**
     * Get the currently authenticated user or null.
     */
    public function user()
    {
        return Auth::user();
    }

    /**
     * Invite an email adress to a team.
     * If no team is given, the current_team_id will be used instead.
     *
     * @param $email
     * @param null|Team $team
     * @param callable $success
     */
    public function inviteToTeam( $email, $team = null, callable $success = null )
    {
        if ( is_null( $team ) )
        {
            $team = $this->user->currentTeam;
        }

        $invite               = new TeamInvite();
        $invite->user_id      = $this->user()->getKey();
        $invite->team_id      = $team->getKey();
        $invite->type         = 'invite';
        $invite->email        = $email;
        $invite->accept_token = md5( uniqid( microtime() ) );
        $invite->deny_token   = md5( uniqid( microtime() ) );
        $invite->save();

        if ( !is_null( $success ) )
        {
            $success( $invite );
        }
    }

    /**
     * Checks if the given email address has a pending invite for the
     * provided Team
     * @param $email
     * @param Team $team
     * @return bool
     */
    public function hasPendingInvite( $email, $team )
    {
        return TeamInvite::where('email', $email)->where('team_id', $team->getKey())->first() ? true : false;
    }

    /**
     * @param $token
     * @return mixed
     */
    public function getInviteFromAcceptToken( $token )
    {
        return TeamInvite::where('accept_token', '=', $token)->first();
    }

    /**
     * @param TeamInvite $invite
     */
    public function acceptInvite( TeamInvite $invite )
    {
        $this->user->attachTeam( $invite->team );
        $invite->delete();
    }

    /**
     * @param $token
     * @return mixed
     */
    public function getInviteFromDenyToken( $token )
    {
        return TeamInvite::where('deny_token', '=', $token)->first();
    }

    /**
     * @param TeamInvite $invite
     */
    public function denyInvite( TeamInvite $invite )
    {
        $invite->delete();
    }
}