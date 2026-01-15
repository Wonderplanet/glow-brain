using System.Collections.Generic;

namespace GLOW.Scenes.BeginnerMission.Presentation.ViewModel
{
    public interface IBeginnerMissionContentViewModel
    {
        public IReadOnlyList<IBeginnerMissionCellViewModel> BeginnerMissionCellViewModels { get; }
    }
}