using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ShopConditionPackExpiredException : DataInconsistencyServerErrorException
    {
        public ShopConditionPackExpiredException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
