using System;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.Transitions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Title.Domains.UseCase;
using UIKit;
using WonderPlanet.SceneManagement;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class StartStageWireFrame
    {
        [Inject] ISelectStageUseCase SelectStageUseCase { get; }
        [Inject] HomeStartStageUseCase StartStageUseCase { get; }
        [Inject] ISceneNavigation SceneNavigation { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        public void StartStage(UIView view, MasterDataId mstStageId, StaminaBoostCount staminaBoostCount, Action<bool> onEndedStartStage, bool isChallengeAd = false)
        {
            DoAsync.Invoke(view, async cancellationToken =>
            {
                var didStartStage = true;
                SelectStageUseCase.SelectStage(mstStageId, MasterDataId.Empty, ContentSeasonSystemId.Empty);
                try
                {
                    await StartStageUseCase.StartStage(cancellationToken, mstStageId, staminaBoostCount, isChallengeAd);
                }
                catch (StageCanNotStartException)
                {
                    // 開放できないステージ・期間外
                    ShowCanNotStartMessage();
                    didStartStage = false;
                    return;
                }
                catch (LackOfResourcesException)
                {
                    // スタミナ不足
                    ShowStaminaLessMessage();
                    didStartStage = false;
                    return;
                }
                finally
                {
                    onEndedStartStage?.Invoke(didStartStage);
                }

                SoundEffectPlayer.Play(SoundEffectId.SSE_012_003);
                SceneNavigation.Switch<InGameTransition>(default, "InGame").Forget();
            });
        }

        void ShowCanNotStartMessage()
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "挑戦できないステージです。",
                "");
        }
        void ShowStaminaLessMessage()
        {
            MessageViewUtil.ShowMessageWithClose(
                "確認",
                "スタミナが不足しています。",
                "");
        }
    }
}
