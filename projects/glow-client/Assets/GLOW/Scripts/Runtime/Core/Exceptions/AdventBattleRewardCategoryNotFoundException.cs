using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class AdventBattleRewardCategoryNotFoundException : WrappedServerErrorException
    {
        public AdventBattleRewardCategoryNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
