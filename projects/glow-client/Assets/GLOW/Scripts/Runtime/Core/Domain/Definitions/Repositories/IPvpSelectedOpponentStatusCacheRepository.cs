using GLOW.Core.Domain.Models.Pvp;

namespace GLOW.Core.Domain.Repositories
{
    public interface IPvpSelectedOpponentStatusCacheRepository
    {
        OpponentPvpStatusModel GetOpponentStatus();
        void SetOpponentStatus(OpponentPvpStatusModel opponentPvpStatus);
        void ClearOpponentStatus();
    }
}
