using UnityHTTPLibrary;

namespace GLOW.Core.Domain.Modules.Network
{
    public interface IHttpRequestFactoryCreator
    {
        IHTTPRequestFactory Create(ITLSCertificateHandler certificateHandler);
    }
}