%global composer_vendor  fkooman
%global composer_project rest-plugin-authentication

%global github_owner     fkooman
%global github_name      php-lib-rest-plugin-authentication

Name:       php-%{composer_vendor}-%{composer_project}
Version:    1.0.0
Release:    1%{?dist}
Summary:    Authentication plugin for fkooman/rest

Group:      System Environment/Libraries
License:    ASL 2.0
URL:        https://github.com/%{github_owner}/%{github_name}
Source0:    https://github.com/%{github_owner}/%{github_name}/archive/%{version}.tar.gz
BuildArch:  noarch

Provides:   php-composer(%{composer_vendor}/%{composer_project}) = %{version}

Requires:   php(language) >= 5.4
Requires:   php-spl

Requires:   php-password-compat >= 1.0.0

Requires:   php-composer(fkooman/rest) >= 1.0.0
Requires:   php-composer(fkooman/rest) < 2.0.0

%description
Library written in PHP to make it easy to develop REST applications.

%prep
%setup -qn %{github_name}-%{version}

%build

%install
mkdir -p ${RPM_BUILD_ROOT}%{_datadir}/php
cp -pr src/* ${RPM_BUILD_ROOT}%{_datadir}/php

%files
%defattr(-,root,root,-)
%dir %{_datadir}/php/%{composer_vendor}/Rest/Plugin/Authentication
%{_datadir}/php/%{composer_vendor}/Rest/Plugin/Authentication/*
%doc README.md CHANGES.md composer.json
%license COPYING

%changelog
* Mon Jul 20 2015 François Kooman <fkooman@tuxed.net> - 1.0.0-1
- initial package
