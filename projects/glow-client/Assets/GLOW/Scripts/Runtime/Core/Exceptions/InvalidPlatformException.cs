using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class InvalidPlatformException : WrappedServerErrorException
    {
        public InvalidPlatformException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
