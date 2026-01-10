using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class UserAccountBanPermanentException : WrappedServerErrorException
    {
        public UserAccountBanPermanentException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
