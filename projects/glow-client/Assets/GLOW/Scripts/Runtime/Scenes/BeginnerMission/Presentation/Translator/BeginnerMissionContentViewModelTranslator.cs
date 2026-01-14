using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.BeginnerMission.Presentation.ViewModel;

namespace GLOW.Scenes.BeginnerMission.Presentation.Translator
{
    public class BeginnerMissionContentViewModelTranslator
    {
        public static IBeginnerMissionContentViewModel ToBeginnerMissionContentViewModel(
            IBeginnerMissionMainViewModel mainViewModel, BeginnerMissionDayNumber currentDayNumber)
        {
            mainViewModel.BeginnerMissionCellViewModelsDictionary.TryGetValue(currentDayNumber, out var cellViewModels);
            return new BeginnerMissionContentViewModel(cellViewModels);
        }
    }
}