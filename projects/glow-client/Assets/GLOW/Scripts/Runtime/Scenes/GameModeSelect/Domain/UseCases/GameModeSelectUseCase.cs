using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.GameModeSelect.Domain
{
    public class GameModeSelectUseCase
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstEventDataRepository MstEventDataRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        public GameModeSelectUseCaseModel GetGameModeSelectUseCaseModel()
        {
            var selectedGameMode = GameModeType.MeinQuest;
            if (!PreferenceRepository.CurrentHomeTopSelectMstQuestId.IsEmpty())
            {
                selectedGameMode = MstQuestDataRepository
                    .GetMstQuestModel(PreferenceRepository.CurrentHomeTopSelectMstQuestId)
                    .QuestType.ToGameModeType();
            }

            var mainQuest =
                GameModeSelectUseCaseItemModel.MainQuest with
                {
                    IsSelected =
                        selectedGameMode == GameModeType.MeinQuest
                            ? GameModeSelectingFlag.True
                            : GameModeSelectingFlag.False,
                };

            var targetEvents = MstEventDataRepository.GetEvents()
                .Where(e => e.StartAt <= TimeProvider.Now && TimeProvider.Now < e.EndAt)
                .Select(e =>
                {
                    var mstQuests = MstQuestDataRepository.GetMstQuestModelsFromEvent(e.Id);
                    var isSelected = selectedGameMode == GameModeType.Event && mstQuests.Exists(m => m.Id == PreferenceRepository.CurrentHomeTopSelectMstQuestId)
                        ? GameModeSelectingFlag.True
                        : GameModeSelectingFlag.False;

                    return new GameModeSelectUseCaseItemModel(
                        isSelected,
                        GameModeType.Event,
                        e.Id,
                        e.EndAt,
                        new GameModeSelectAssetKey(e.AssetKey.Value),
                        e.AssetKey,
                        e.EndAt - TimeProvider.Now);
                });

            var items =
                new List<GameModeSelectUseCaseItemModel>() { mainQuest }.Concat(targetEvents);

            return new GameModeSelectUseCaseModel(items.ToList());
        }
    }
}
