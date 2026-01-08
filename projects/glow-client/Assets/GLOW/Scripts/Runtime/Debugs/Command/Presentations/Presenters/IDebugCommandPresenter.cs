using System;
using System.Collections.Generic;

namespace GLOW.Debugs.Command.Presentations.Presenters
{
    public interface IDebugCommandPresenter
    {
        Action<IDebugCommandPresenter> DidLoad { get; set; }
        Action DidUnload { get; set; }
        Action<IDebugCommandPresenter> CreateRootMenu { get; set; }

        void AddButton(string text, Action onTapped);
        void AddToggleButton(string text, bool isOn, Action<bool> onTapped);
        void AddTextBox(string text, string defaultInputFieldText, Action<string> onEndEdit);

        void AddInputToggle(string text, string defaultInputFieldText, bool isOn, Action<bool> onTapped,
            Action<string> onEndEdit);

        void AddDropdownInputToggle(string text, List<(bool, string)> defaultValue, Action<List<(bool, string)>> onValueChanged);
        void AddStateButton(string text, string[] states,string defaultValue, Action<string> onStateChanged);
        void AddNestedMenuButton(string text, Action<IDebugCommandPresenter> createNestedMenu);
        void CloseMenu();
        void UpdateMenu(IDebugCommandPresenter debugCommandPresenter, Action<IDebugCommandPresenter> createMenuAction);
    }
}
