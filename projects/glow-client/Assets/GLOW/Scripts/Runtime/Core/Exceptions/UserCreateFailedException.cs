using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserCreateFailedException : WrappedServerErrorException
    {
        public UserCreateFailedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
