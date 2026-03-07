using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Scenes.InGame.Domain.Repositories
{
    public interface IInGameLogRepository
    {
        void SetLog(InGameLogModel inGameLogModel);
        InGameLogModel GetLog();
    }
}
