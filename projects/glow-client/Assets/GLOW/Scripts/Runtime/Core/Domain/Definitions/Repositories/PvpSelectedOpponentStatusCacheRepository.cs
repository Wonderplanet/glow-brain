using GLOW.Core.Domain.Models.Pvp;

namespace GLOW.Core.Domain.Repositories
{
    public class PvpSelectedOpponentStatusCacheRepository : IPvpSelectedOpponentStatusCacheRepository
    {
        OpponentPvpStatusModel _opponentPvpStatus = OpponentPvpStatusModel.Empty;

        OpponentPvpStatusModel IPvpSelectedOpponentStatusCacheRepository.GetOpponentStatus()
        {
            return _opponentPvpStatus;
        }

        void IPvpSelectedOpponentStatusCacheRepository.SetOpponentStatus(OpponentPvpStatusModel opponentPvpStatus)
        {
            _opponentPvpStatus = opponentPvpStatus;
        }

        void IPvpSelectedOpponentStatusCacheRepository.ClearOpponentStatus()
        {
            _opponentPvpStatus = OpponentPvpStatusModel.Empty;
        }
    }
}
