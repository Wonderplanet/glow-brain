using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class MultipleDeviceLoginDetectedException : WrappedServerErrorException
    {
        public MultipleDeviceLoginDetectedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
