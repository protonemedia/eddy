<?php

namespace App\Server;

use Illuminate\Support\Str;

enum ProvisionStep: string
{
    case AptUpdateUpgrade = 'apt_update_upgrade';
    case ConfigureFirewall = 'configure_firewall';
    case ConfigureSwap = 'configure_swap';
    case InstallEssentialPackages = 'install_essential_packages';
    case SetupDefaultUser = 'setup_default_user';
    case SetupRoot = 'setup_root';
    case SetupUnattendedUpgrades = 'setup_unattended_upgrades';
    case SshSecurity = 'ssh_security';

    /**
     * Returns an array with the default steps to provision a server.
     */
    public static function forFreshServer(): array
    {
        return [
            self::ConfigureSwap,
            self::ConfigureFirewall,
            self::AptUpdateUpgrade,
            self::InstallEssentialPackages,
            self::SetupUnattendedUpgrades,
            self::SetupRoot,
            self::SshSecurity,
            self::SetupDefaultUser,
        ];
    }

    /**
     * Returns the description of the step.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::AptUpdateUpgrade => __('Update package lists and installed system packages'),
            self::ConfigureFirewall => __('Configure the firewall with the default rules (SSH, HTTP, HTTPS)'),
            self::ConfigureSwap => __('Configure a swap file so the server can handle more memory-intensive tasks'),
            self::InstallEssentialPackages => __('Install essential packages (:examples)', ['examples' => 'curl, git, wget, etc.']),
            self::SetupDefaultUser => __('Create a default user account'),
            self::SetupRoot => __('Configure the root user'),
            self::SetupUnattendedUpgrades => __('Configure unattended upgrades to keep the server up to date automatically'),
            self::SshSecurity => __('Enhance SSH security by disabling password authentication and root login'),
        };
    }

    /**
     * Returns the Blade view name for the step.
     */
    public function getViewName(): string
    {
        return 'tasks.provision.'.Str::replace('_', '-', $this->value);
    }
}
