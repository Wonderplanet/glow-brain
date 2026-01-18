using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.BeginnerMission.Domain.Model;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Domain.UseCase
{
    public class GetBeginnerMissionPromptPhraseUseCase
    {
        [Inject] IMstMissionDataRepository MstMissionDataRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public MissionBeginnerPromptPhraseUseCaseModel GetBeginnerMissionReceivableTotalDiamondAmount()
        {
            var beginnerMissionPromptPhraseModels = MstMissionDataRepository.GetMstMissionBeginnerPromptPhraseModels();
            var nowTime = TimeProvider.Now;

            var beginnerMissionPromptPhrase = beginnerMissionPromptPhraseModels
                .FirstOrDefault(
                    model => CalculateTimeCalculator.IsValidTime(nowTime, model.StartDate, model.EndDate),
                    MstMissionBeginnerPromptPhraseModel.Empty);
            
            return beginnerMissionPromptPhrase.IsEmpty() 
                ? MissionBeginnerPromptPhraseUseCaseModel.Empty 
                : new MissionBeginnerPromptPhraseUseCaseModel(beginnerMissionPromptPhrase.BeginnerMissionPromptPhrase);
        }
    }
}
