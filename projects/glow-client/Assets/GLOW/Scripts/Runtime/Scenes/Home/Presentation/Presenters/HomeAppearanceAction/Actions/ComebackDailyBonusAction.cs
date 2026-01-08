using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.UseCases;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    public class ComebackDailyBonusAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<ComebackDailyBonusAction> { }

        [Inject] HomeMissionWireFrame HomeMissionWireFrame { get; }
        [Inject] CheckExistComebackDailyBonusUseCase CheckExistComebackDailyBonusUseCase { get; }
        
        public async UniTask ExecuteAsync(
            HomeAppearanceActionContext context, 
            Action onCloseCompletion,
            CancellationToken cancellationToken)
        {
            var currentComebackBonusModel = CheckExistComebackDailyBonusUseCase.GetCurrentComebackDailyBonus();
            if (!currentComebackBonusModel.IsDisplayAtLogin) return;
            
            await UniTask.Delay(TimeSpan.FromSeconds(0.1f), cancellationToken: cancellationToken);
            
            await HomeMissionWireFrame.ShowComebackDailyBonusView(
                currentComebackBonusModel.MstComebackDailyBonusScheduleId, 
                cancellationToken);
        }
    }
}