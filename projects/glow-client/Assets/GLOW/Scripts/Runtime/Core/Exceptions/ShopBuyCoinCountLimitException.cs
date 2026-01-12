using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ShopBuyCoinCountLimitException : WrappedServerErrorException
    {
        public ShopBuyCoinCountLimitException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
