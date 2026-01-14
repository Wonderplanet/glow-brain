using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ExpiredMessageResourceException : WrappedServerErrorException
    {
        public ExpiredMessageResourceException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
