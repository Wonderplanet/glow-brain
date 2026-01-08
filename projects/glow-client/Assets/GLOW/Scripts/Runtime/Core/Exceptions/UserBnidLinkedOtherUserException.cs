using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserBnidLinkedOtherUserException : WrappedServerErrorException
    {
        public UserBnidLinkedOtherUserException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
