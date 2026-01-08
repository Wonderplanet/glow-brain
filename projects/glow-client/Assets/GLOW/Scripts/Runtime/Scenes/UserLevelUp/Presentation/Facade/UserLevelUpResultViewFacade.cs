using System;
using GLOW.Scenes.UserLevelUp.Presentation.ViewModel;
using GLOW.Scenes.UserLevelUp.Presentation.View;
using UIKit;
using WPFramework.Constants.Zenject;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UserLevelUp.Presentation.Facade
{
    public class UserLevelUpResultViewFacade : IUserLevelUpResultViewFacade
    {
        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }
        [Inject] IViewFactory ViewFactory { get; }

        public void Show(UserLevelUpResultViewModel viewModel)
        {
            if(viewModel.IsEmpty()) return;

            var argument = new UserLevelUpViewController.Argument(viewModel, () => { });
            var controller = ViewFactory.Create<UserLevelUpViewController, UserLevelUpViewController.Argument>(argument);
            Canvas.RootViewController.PresentModally(controller);
        }

        public void ShowWithClosedAction(UserLevelUpResultViewModel viewModel, Action onClosed)
        {
            if(viewModel.IsEmpty()) return;

            var argument = new UserLevelUpViewController.Argument(viewModel, onClosed);
            var controller = ViewFactory.Create<UserLevelUpViewController, UserLevelUpViewController.Argument>(argument);
            Canvas.RootViewController.PresentModally(controller);
        }
    }
}
