
Name: app-beams
Epoch: 1
Version: 1.0.0
Release: 1%{dist}
Summary: Beam Manager
License: GPLv3
Group: ClearOS/Apps
Vendor: Marine VSAT
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base

%description
Manage various aspects of the modem/beams used to connect to the Internet.

%package core
Summary: Beam Manager - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-firewall-custom-core

%description core
Manage various aspects of the modem/beams used to connect to the Internet.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/beams
cp -r * %{buildroot}/usr/clearos/apps/beams/

install -d -m 755 %{buildroot}/var/clearos/beams.d
install -D -m 0644 packaging/app-beams.cron %{buildroot}/etc/cron.d/app-beams
install -D -m 0644 packaging/beams.conf %{buildroot}/etc/clearos/beams.conf

%post
logger -p local6.notice -t installer 'app-beams - installing'

%post core
logger -p local6.notice -t installer 'app-beams-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/beams/deploy/install ] && /usr/clearos/apps/beams/deploy/install
fi

[ -x /usr/clearos/apps/beams/deploy/upgrade ] && /usr/clearos/apps/beams/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-beams - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-beams-core - uninstalling'
    [ -x /usr/clearos/apps/beams/deploy/uninstall ] && /usr/clearos/apps/beams/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/beams/controllers
/usr/clearos/apps/beams/htdocs
/usr/clearos/apps/beams/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/beams/packaging
%dir /usr/clearos/apps/beams
%dir /var/clearos/beams.d
/usr/clearos/apps/beams/deploy
/usr/clearos/apps/beams/language
/usr/clearos/apps/beams/libraries
/etc/cron.d/app-beams
%attr(0644,webconfig,webconfig) %config(noreplace) /etc/clearos/beams.conf
