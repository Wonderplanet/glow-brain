using System.Collections.Generic;

namespace GLOW.Scenes.BeginnerMission.Presentation.ViewModel
{
    public class BeginnerMissionContentViewModel : IBeginnerMissionContentViewModel
    {
        public IReadOnlyList<IBeginnerMissionCellViewModel> BeginnerMissionCellViewModels { get; }
        
        public BeginnerMissionContentViewModel(IReadOnlyList<IBeginnerMissionCellViewModel> beginnerMissionCellViewModels)
        {
            BeginnerMissionCellViewModels = beginnerMissionCellViewModels;
        }
    }
}