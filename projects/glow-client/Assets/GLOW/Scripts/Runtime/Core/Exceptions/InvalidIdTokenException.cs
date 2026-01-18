using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class InvalidIdTokenException : WrappedServerErrorException
    {
        public InvalidIdTokenException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
