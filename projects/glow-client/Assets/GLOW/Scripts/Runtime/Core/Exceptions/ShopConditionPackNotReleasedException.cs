using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ShopConditionPackNotReleasedException : DataInconsistencyServerErrorException
    {
        public ShopConditionPackNotReleasedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
