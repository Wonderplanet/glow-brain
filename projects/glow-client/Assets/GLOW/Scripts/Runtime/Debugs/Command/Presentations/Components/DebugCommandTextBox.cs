using System;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace GLOW.Debugs.Command.Presentations.Components
{
    public class DebugCommandTextBox : UIBehaviour
    {
        [SerializeField] Text _labelText;
        [SerializeField] InputField _inputField;

        public string Text
        {
            set => _labelText.text = value;
        }

        public string DefaultInputFieldText
        {
            set => _inputField.text = value;
        }

        public Action<string> OnEndEdit { get; set; }

        protected override void Awake()
        {
            base.Awake();

            _inputField.onEndEdit.AddListener(delegate
            {
                OnEndEdit.Invoke(_inputField.text);
            });
        }
    }
}
