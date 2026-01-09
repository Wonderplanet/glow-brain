using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserAccountSuspendedException : WrappedServerErrorException
    {
        public UserAccountSuspendedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
