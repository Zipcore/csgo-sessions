<?php

namespace App\Services;

use App\Classes\SessionManager;
use App\Exceptions\MultipleSessionsException;
use App\Exceptions\UntrackedServerException;
use App\Server;
use App\Session;
use Exception;

class SessionService
{
	public function closeActiveSession($steamid)
	{
		$sessions = Session::where('steamid', $steamid)->where('active', true)->get();

		if ($sessions->count() > 1)
			throw new MultipleSessionsException($steamid);

		$session = $sessions->first();
		$session->steamid = $steamid;
		$session->active = false;
		$session->save();
	}

	public function getActiveSessions()
	{
		return Session::where('active', true)->join('servers', 'sessions.server_id', '=', 'servers.id')->get();
	}

	/** @deprecated */
	public function getActiveSessionManagersByServer()
	{
		return $this->getActiveSessions()->mapToGroups(function ($session) {
			return [$session['address'] => new SessionManager($session)];
		})->toArray();
	}

	public function create($steamid, $serverAdress)
	{
		// TODO: server should ALWAYS exist on database because it should be filtered somewhere else
		$server = Server::where('address', $serverAdress)->first();

		if (!$server)
			throw new UntrackedServerException($serverAdress);

		// TODO: fillable
		$session = Session::make();

		$session->active = true;
		$session->server()->associate($server);
		$session->steamid = $steamid;

		$session->save();

		return $session;
	}
}