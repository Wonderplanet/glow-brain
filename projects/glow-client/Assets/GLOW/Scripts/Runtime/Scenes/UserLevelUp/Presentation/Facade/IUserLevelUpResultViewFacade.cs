using System;
using GLOW.Scenes.UserLevelUp.Presentation.ViewModel;

namespace GLOW.Scenes.UserLevelUp.Presentation.Facade
{
    public interface IUserLevelUpResultViewFacade
    {
        public void Show(UserLevelUpResultViewModel viewModel);
        
        void ShowWithClosedAction(UserLevelUpResultViewModel viewModel, Action onClosed);
    }
}