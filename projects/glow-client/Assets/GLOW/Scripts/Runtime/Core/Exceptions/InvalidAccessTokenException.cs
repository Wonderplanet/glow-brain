using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class InvalidAccessTokenException : WrappedServerErrorException
    {
        public InvalidAccessTokenException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
