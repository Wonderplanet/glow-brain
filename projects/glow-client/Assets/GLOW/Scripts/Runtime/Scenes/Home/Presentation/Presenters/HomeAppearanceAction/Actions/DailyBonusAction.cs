using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Home.Domain.UseCases;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// デイリーボーナス表示
    /// </summary>
    public class DailyBonusAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<DailyBonusAction> { }

        [Inject] HomeMissionWireFrame HomeMissionWireFrame { get; }
        [Inject] CheckExistLoginBonusUseCase CheckExistLoginBonusUseCase { get; }

        public async UniTask ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion,
            CancellationToken cancellationToken)
        {
            var isExistDailyBonus = await CheckExistLoginBonusUseCase.IsExistLoginBonus(MissionType.DailyBonus, cancellationToken);
            if (!isExistDailyBonus) return;

            await UniTask.Delay(TimeSpan.FromSeconds(0.1f), cancellationToken: cancellationToken);

            MissionClosedByChallengeFlag isMissionClosedByChallenge = await HomeMissionWireFrame.ShowMissionView(
                true,
                MissionType.DailyBonus,
                false,
                cancellationToken);

            // ミッションに挑戦する場合以降をキャンセルする
            if (isMissionClosedByChallenge)
            {
                throw new OperationCanceledException(cancellationToken);
            }
        }
    }
}
