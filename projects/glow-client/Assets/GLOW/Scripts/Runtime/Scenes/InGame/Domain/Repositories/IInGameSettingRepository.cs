using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Repositories
{
    public interface IInGameSettingRepository
    {
        InGameSettingModel GetInGameSetting();
    }
}
