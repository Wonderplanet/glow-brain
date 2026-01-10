using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserNotFoundException : WrappedServerErrorException
    {
        public UserNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
