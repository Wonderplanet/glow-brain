using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.BeginnerMission.Domain.Model;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Domain.UseCase
{
    public class CheckBeginnerMissionDayUnlockUseCase
    {
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        
        public MissionBeginnerDayUnlockUseCaseModel CheckBeginnerMissionStartDay(BeginnerMissionDaysFromStart daysFromStart)
        {
            var savedUnlockedDay = PreferenceRepository.BeginnerMissionReleaseDayNumber;
            if (savedUnlockedDay < daysFromStart.Value)
            {
                PreferenceRepository.SetBeginnerMissionReleaseDayNumber(daysFromStart.Value);
            }
            
            return new MissionBeginnerDayUnlockUseCaseModel(new BeginnerMissionDaysFromStart(savedUnlockedDay), daysFromStart);
        }
    }
}