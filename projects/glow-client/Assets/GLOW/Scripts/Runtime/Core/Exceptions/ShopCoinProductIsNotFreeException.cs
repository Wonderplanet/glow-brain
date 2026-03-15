using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ShopCoinProductIsNotFreeException : WrappedServerErrorException
    {
        public ShopCoinProductIsNotFreeException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
