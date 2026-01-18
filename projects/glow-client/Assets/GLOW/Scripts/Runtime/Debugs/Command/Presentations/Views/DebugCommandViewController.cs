#if GLOW_DEBUG
using System.Collections.Generic;
using GLOW.Debugs.Command.Presentations.ViewModels;
#endif //GLOW_DEBUG
using System;
using System.Globalization;
using UIKit;
using Zenject;

namespace GLOW.Debugs.Command.Presentations.Views
{
    public sealed class DebugCommandViewController : UIViewController<DebugCommandView>
    {
#if GLOW_DEBUG
        [Inject] IDebugCommandViewDelegate DebugCommandViewDelegate { get; }

        const string TimeFormat = "yyyy-MM-dd HH:mm:ss zzz";

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            DebugCommandViewDelegate.OnViewDidLoad(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            DebugCommandViewDelegate.ViewDidUnload();
        }

        public void SetViewModel(DebugCommandViewModel viewModel)
        {
            ActualView.ApplicationTimeText =
                viewModel.TimeViewModel.CurrentTime.ToLocalTime().ToString(TimeFormat, CultureInfo.InvariantCulture);
            ActualView.ApplicationEnvText = viewModel.EnvName.Value;
        }

        public void SetTimeViewModel(DebugCommandTimeViewModel viewModel)
        {
            ActualView.ApplicationTimeText = viewModel.CurrentTime.ToLocalTime().ToString(TimeFormat, CultureInfo.InvariantCulture);
        }

        public void ClearMenu()
        {
            ActualView.ClearMenu();
        }

        public void AddButton(string text, Action onTapped)
        {
            ActualView.AddButton(text, onTapped);
        }

        public void AddToggleButton(string text, bool isOn, Action<bool> onTapped)
        {
            ActualView.AddToggleButton(text, isOn, onTapped);
        }

        public void AddTextBox(string text, string defaultInputFieldText, Action<string> onEndEdit)
        {
            ActualView.AddTextBox(text, defaultInputFieldText, onEndEdit);
        }

        public void AddInputToggle(string text, string defaultInputFieldText, bool isOn, Action<bool> onTapped, Action<string> onEndEdit)
        {
            ActualView.AddInputToggle(text, defaultInputFieldText, isOn, onTapped, onEndEdit);
        }

        public void AddDropdownInputToggle(string text, List<(bool, string)> defaultValue, Action<List<(bool, string)>> onValueChanged)
        {
            ActualView.AddDropdownInputToggle(text, defaultValue, onValueChanged);
        }

        public void AddStateButton(string text, string[] states, string defaultValue, Action<string> onChanged)
        {
            ActualView.AddStateButton(text, states, defaultValue, onChanged);
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            //InGameポーズ画面のようなTime.timeScale0でも非表示できるようにanimated falseにしている
            Dismiss(false);
        }

        public void ViewClose()
        {
            //InGameポーズ画面のようなTime.timeScale0でも非表示できるようにanimated falseにしている
            Dismiss(false);
        }
#endif //GLOW_DEBUG
    }
}
