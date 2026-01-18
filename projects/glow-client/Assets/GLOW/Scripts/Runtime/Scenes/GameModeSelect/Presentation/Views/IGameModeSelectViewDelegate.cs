using System;
using System.Threading;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.GameModeSelect.Domain;

namespace GLOW.Scenes.GameModeSelect.Presentation
{
    public interface IGameModeSelectViewDelegate
    {
        void OnViewWillAppear(GameModeSelectViewController viewController);
        void OnGameModeButtonTap(
            GameModeType modelType,
            EventAssetKey eventAssetKey,
            MasterDataId modelMstEventId,
            DateTimeOffset endAt,
            Action onSelectGameMode);
    }
}
