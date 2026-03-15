using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class CheckExistComebackDailyBonusUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IReceivedComebackDailyBonusRepository ReceivedComebackDailyBonusRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        
        public CurrentComebackDailyBonusModel GetCurrentComebackDailyBonus()
        {
            var comebackDailyBonusProgresses = GameRepository.GetGameFetchOther().UserComebackBonusProgressModels;
            var currentComebackDailyBonusProgress = comebackDailyBonusProgresses
                .FirstOrDefault(UserComebackBonusProgressModel.Empty);
            if (currentComebackDailyBonusProgress.IsEmpty()) return CurrentComebackDailyBonusModel.Empty;
            
            var receivedComebackDailyBonusRewards = GameRepository.GetGameFetchOther().ComebackBonusRewardModels;
            var isExistComebackDailyBonus = !receivedComebackDailyBonusRewards.IsEmpty() || 
                                            ReceivedComebackDailyBonusRepository.IsExist();
            
            var isDisplayAtLogin =  isExistComebackDailyBonus 
                ? DisplayAtLoginFlag.True 
                : DisplayAtLoginFlag.False;
            
            return new CurrentComebackDailyBonusModel(
                currentComebackDailyBonusProgress.MstComebackBonusScheduleId,
                isDisplayAtLogin,
                comebackDailyBonusProgresses.Any(
                    model => CalculateTimeCalculator.IsValidTime(
                        TimeProvider.Now,
                        model.StartAt.Value,
                        model.EndAt.Value)));
        }
    }
}