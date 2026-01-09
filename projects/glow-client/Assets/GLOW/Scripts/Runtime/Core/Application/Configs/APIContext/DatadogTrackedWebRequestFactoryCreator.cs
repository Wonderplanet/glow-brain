using GLOW.Core.Domain.Modules.Network;
using UnityHTTPLibrary;
using WPFramework.Modules.Observability;

namespace GLOW.Core.Application.Configs.APIContext
{
    public sealed class DatadogTrackedWebRequestFactoryCreator : IHttpRequestFactoryCreator
    {
        public IHTTPRequestFactory Create(ITLSCertificateHandler certificateHandler)
        {
            return new DatadogTrackedWebRequestFactory(certificateHandler);
        }
    }
}