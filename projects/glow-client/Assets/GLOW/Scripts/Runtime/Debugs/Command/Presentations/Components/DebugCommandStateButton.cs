using System.Collections.Generic;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;
using System;
using Cysharp.Text;

namespace GLOW.Debugs.Command.Presentations.Components
{
    public class DebugCommandStateButton : UIBehaviour
    {
        [SerializeField] Text _labelText;
        [SerializeField] Button _button;
        List<string> _states = new List<string>();
        int _currentStateIndex = 0;

        public string LabelTextTemp = string.Empty;
        public string Text
        {
            set => _labelText.text = value;
        }
        public List<string> States
        {
            get => _states;
            set
            {
                _states = value;
            }
        }
        public string CurrentValue
        {
            get
            {
                if (_states.Count == 0) return string.Empty;
                return _states[_currentStateIndex];
            }
            set
            {
                if (_states.Count == 0) return;
                var index = _states.IndexOf(value);
                if (index >= 0)
                {
                    _currentStateIndex = index;
                    Text = ZString.Format("{0} : {1}", LabelTextTemp, _states[_currentStateIndex]);
                }
            }
        }

        public Action<string> OnChangeState { get; set; }

        protected override void Awake()
        {
            _button.onClick.AddListener(() =>
            {
                if (_states.Count - 1 > _currentStateIndex)
                {
                    _currentStateIndex++;
                    Text = ZString.Format("{0} : {1}", LabelTextTemp, _states[_currentStateIndex]);
                }
                else
                {
                    _currentStateIndex = 0;
                    Text = ZString.Format("{0} : {1}", LabelTextTemp, _states[_currentStateIndex]);
                }

                OnChangeState?.Invoke(_states[_currentStateIndex]);
            });
        }
    }
}

