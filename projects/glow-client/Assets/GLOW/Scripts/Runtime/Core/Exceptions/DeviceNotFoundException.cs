using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class DeviceNotFoundException : WrappedServerErrorException
    {
        public DeviceNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
