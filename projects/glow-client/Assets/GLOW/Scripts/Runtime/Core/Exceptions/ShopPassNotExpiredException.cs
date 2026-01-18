using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ShopPassNotExpiredException : DataInconsistencyServerErrorException
    {
        public ShopPassNotExpiredException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
