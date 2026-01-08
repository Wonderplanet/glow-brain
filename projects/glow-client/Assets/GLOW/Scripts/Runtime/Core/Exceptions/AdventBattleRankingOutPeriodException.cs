using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class AdventBattleRankingOutPeriodException : DataInconsistencyServerErrorException
    {
        public AdventBattleRankingOutPeriodException(ServerErrorException innerException) : base(innerException)
        {
        }
    }
}
