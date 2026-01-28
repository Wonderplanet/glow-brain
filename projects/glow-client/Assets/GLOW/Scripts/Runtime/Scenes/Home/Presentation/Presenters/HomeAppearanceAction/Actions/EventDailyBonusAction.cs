using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.QuestContentTop.Domain;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// イベントデイリーボーナス表示
    /// </summary>
    public class EventDailyBonusAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<EventDailyBonusAction> { }

        [Inject] HomeMissionWireFrame HomeMissionWireFrame { get; }
        [Inject] CheckExistLoginBonusUseCase CheckExistLoginBonusUseCase { get; }
        [Inject] GetLatestEventUseCase GetLatestEventUseCase { get; }

        async UniTask IHomeAppearanceAction.ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion, CancellationToken cancellationToken)
        {
            var isExistEventDailyBonus = await CheckExistLoginBonusUseCase.IsExistLoginBonus(MissionType.EventDailyBonus, cancellationToken);
            if (!isExistEventDailyBonus)  return;

            await UniTask.Delay(TimeSpan.FromSeconds(0.1f), cancellationToken: cancellationToken);

            var latestMstEvent = GetLatestEventUseCase.GetLatestMstEventModel();
            MissionClosedByChallengeFlag isMissionClosedByChallenge = await HomeMissionWireFrame.ShowEventMissionView(
                MissionType.EventDailyBonus,
                latestMstEvent.Id,
                onCloseCompletion,
                cancellationToken);

            // ミッションに挑戦する場合以降をキャンセルする
            if (isMissionClosedByChallenge)
            {
                throw new OperationCanceledException(cancellationToken);
            }
        }
    }
}
