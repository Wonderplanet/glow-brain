using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.BeginnerMission.Domain.UseCase;
using GLOW.Scenes.BeginnerMission.Presentation.View;
using GLOW.Scenes.ComebackDailyBonus.Presentation.View;
using GLOW.Scenes.EventMission.Presentation.Facade;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Mission.Presentation.View.MissionMain;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class HomeMissionWireFrame
    {
        [Inject] HomeMainViewController HomeMainViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] HomeMainBadgeUseCase HomeMainBadgeUseCase { get; }
        [Inject] UpdateBeginnerMissionAndPassStatusUseCase UpdateBeginnerMissionAndPassStatusUseCase { get; }
        [Inject] IEventMissionWireFrame EventMissionWireFrame { get; }

        readonly HomeMainViewModelTranslator _viewModelTranslator = new();

        public async UniTask<MissionClosedByChallengeFlag> ShowMissionView(
            bool isFirstLogin,
            MissionType missionType,
            bool isDisplayFromItemDetail,
            CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource<MissionClosedByChallengeFlag>();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var argument = new MissionMainViewController.Argument(isFirstLogin, isDisplayFromItemDetail, missionType);
            var controller = ViewFactory.Create<MissionMainViewController, MissionMainViewController.Argument>(argument);

            // ミッションを閉じた時に即座に表示状態を反映させるため
            controller.OnCloseCompletion = () =>
            {
                HomeMainViewController.UpdateHomeMainBadge(
                    _viewModelTranslator.TranslateToHomeMainBadgeViewModel(HomeMainBadgeUseCase.GetHomeMainBadgeModel()));
                completionSource.TrySetResult(MissionClosedByChallengeFlag.False);
            };

            // DismissByChallengeが呼び出された時のキャンセル処理
            controller.OnDismissByChallenge = () =>
            {
                completionSource.TrySetResult(MissionClosedByChallengeFlag.True);
            };

            HomeMainViewController.PresentModally(controller);

            return await completionSource.Task;
        }

        public async UniTask ShowBeginnerMissionView(CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var controller = ViewFactory.Create<BeginnerMissionMainViewController>();

            // ミッションを閉じた時に即座に表示状態を反映させるため
            controller.OnCloseCompletion = () =>
            {
                // 初心者ミッション状態更新、バッジ情報取得
                var useCaseModel = UpdateBeginnerMissionAndPassStatusUseCase.UpdateBeginnerMissionStatusAndGetPassStatus();
                HomeMainViewController.SetBeginnerMissionVisible(useCaseModel.BeginnerMissionFinishedFlag);

                completionSource.TrySetResult();
            };

            HomeMainViewController.PresentModally(controller);

            await completionSource.Task;
        }

        public async UniTask<MissionClosedByChallengeFlag> ShowEventMissionView(
            MissionType missionType,
            MasterDataId mstEventId,
            Action onCloseCompletion,
            CancellationToken cancellationToken)
        {


            var isMissionChallenge = await EventMissionWireFrame
                .ShowEventMissionViewInHome(
                    HomeMainViewController,
                    missionType,
                    mstEventId,
                    onCloseCompletion,
                    cancellationToken);

            return isMissionChallenge;
        }

        public async UniTask ShowComebackDailyBonusView(
            MasterDataId mstComebackDailyBonusScheduleId,
            CancellationToken cancellationToken)
        {
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            var argument = new ComebackDailyBonusViewController.Argument(mstComebackDailyBonusScheduleId);
            var controller = ViewFactory.Create<
                ComebackDailyBonusViewController,
                ComebackDailyBonusViewController.Argument>(argument);

            // ミッションを閉じた時に即座に表示状態を反映させるため
            controller.OnCloseCompletion = () => { completionSource.TrySetResult(); };

            HomeMainViewController.PresentModally(controller);

            await completionSource.Task;
        }
    }
}

