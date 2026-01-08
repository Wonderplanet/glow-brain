using System;
using System.Threading;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.GameModeSelect.Domain;
using Zenject;

namespace GLOW.Scenes.GameModeSelect.Presentation
{
    public class GameModeSelectPresenter : IGameModeSelectViewDelegate
    {
        [Inject] GameModeSelectUseCase GameModeSelectUseCase { get; }
        [Inject] UpdateCurrentMstQuestIdUseCase UpdateCurrentMstQuestIdUseCase { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        [Inject] IMessageViewUtil MessageViewUtil { get; }

        void IGameModeSelectViewDelegate.OnViewWillAppear(GameModeSelectViewController viewController)
        {
            // 開くたびに開催状況が変化する可能性あるので毎度ロードしている(管理が面倒)
            var useCaseModel = GameModeSelectUseCase.GetGameModeSelectUseCaseModel();
            var viewModel = GameModeSelectViewModelTranslator.Translate(useCaseModel);
            viewController.SetViewModel(viewModel);
        }

        void IGameModeSelectViewDelegate.OnGameModeButtonTap(
            GameModeType modelType,
            EventAssetKey eventAssetKey,
            MasterDataId modelMstEventId,
            DateTimeOffset endAt,
            Action onSelectGameMode)
        {
            if (modelType == GameModeType.MeinQuest)
            {
                UpdateCurrentMstQuestIdUseCase.UpdateCurrentMstQuestId(modelType, modelMstEventId);
                onSelectGameMode?.Invoke();
            }
            else
            {
                if (endAt < TimeProvider.Now)
                {
                    MessageViewUtil.ShowMessageWithClose("確認",
                        "イベントは終了しました。\n次回開催をお待ちください。",
                        "",
                        () =>
                        {
                            UpdateCurrentMstQuestIdUseCase.UpdateCurrentMstQuestId(modelType, modelMstEventId);
                            onSelectGameMode?.Invoke();
                        });
                    return;
                }

                UpdateCurrentMstQuestIdUseCase.UpdateCurrentMstQuestId(modelType, modelMstEventId);
                onSelectGameMode?.Invoke();
            }
        }

    }

}
