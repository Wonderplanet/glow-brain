using System.Collections.Generic;
using System.Linq;
using Framework.Scripts.Runtime.Modules.Network.Certificates;
using GLOW.Core.Constants;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;
using WPFramework.Modules.Log;

namespace GLOW.Core.Application.Configs
{
    public sealed class TLSFullyQualifiedDomainNameResolver : ITLSFullyQualifiedDomainNameResolver
    {
        IReadOnlyCollection<ObscuredString> ITLSFullyQualifiedDomainNameResolver.Resolve()
        {
            // NOTE: 複数のFQDNを指定する場合はセミコロンで区切る
            var fqdns =
                Credentials.CertificateFQDNs.Split(';')
                    .Select(x=> (ObscuredString)x.Trim())
                    .ToList();
#if GLOW_DEBUG
            ApplicationLog.Log(nameof (TLSFullyQualifiedDomainNameResolver), $"TLS FQDN: {string.Join(',', fqdns)}");
#endif
            return fqdns;
        }
    }}