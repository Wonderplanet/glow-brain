using System;
using System.Collections.Generic;
using System.Linq;
using System.Security.Cryptography.X509Certificates;
using UnityHTTPLibrary;
using UnityHTTPLibrary.Certificate;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace Framework.Scripts.Runtime.Modules.Network.Certificates
{
    public sealed class TLSCertificateSubjectAlternativeNameValidator : ITLSCertificateValidator
    {
        readonly HashSet<ObscuredString> _expectedFqdns;

        public TLSCertificateSubjectAlternativeNameValidator(ObscuredString expectedFqdn)
        {
            _expectedFqdns = new HashSet<ObscuredString>() { expectedFqdn };
        }

        public TLSCertificateSubjectAlternativeNameValidator(HashSet<ObscuredString> expectedFqdns)
        {
            _expectedFqdns = expectedFqdns ?? throw new ArgumentNullException(nameof(expectedFqdns));
        }

        bool ITLSCertificateValidator.IsValid(string host, X509Certificate2 certificate)
        {
            return SubjectAlternativeNameValidator.IsValid(certificate, _expectedFqdns.Select(fqdn => (string)fqdn).ToHashSet());
        }
    }
}