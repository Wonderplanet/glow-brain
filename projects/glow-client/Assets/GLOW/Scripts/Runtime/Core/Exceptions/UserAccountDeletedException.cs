using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserAccountDeletedException : WrappedServerErrorException
    {
        public UserAccountDeletedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
