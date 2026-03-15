using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class DeviceLinkAuthFailedException : WrappedServerErrorException
    {
        public DeviceLinkAuthFailedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
