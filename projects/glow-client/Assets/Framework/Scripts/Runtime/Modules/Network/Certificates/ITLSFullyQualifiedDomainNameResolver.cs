using System.Collections.Generic;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace Framework.Scripts.Runtime.Modules.Network.Certificates
{
    public interface ITLSFullyQualifiedDomainNameResolver
    {
        IReadOnlyCollection<ObscuredString> Resolve();
    }
}