using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using System.Collections;
using TMPro;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.PartyNameEdit.Presentation.Views
{
    public class PartyNameEditDialogView : UIView
    {
        [SerializeField] TMP_InputField _inputField;
        [SerializeField] Button _saveButton;
        [SerializeField] UIText _inputTextCount;
        [SerializeField] UIText _inputMaxLength;
        [SerializeField] UIText _messageText;

        Vector2 _inputFieldAnchorPosition;
        Vector2 _inputFieldCaretAnchorPosition;
        TMP_SelectionCaret _inputFieldCaret;

        protected override void Awake()
        {
            base.Awake();

            _inputFieldAnchorPosition = _inputField.textComponent.rectTransform.anchoredPosition;
            StartCoroutine(GetInputFieldCaret());

            _inputField.onValueChanged.AddListener(text =>
            {
                _saveButton.interactable = !string.IsNullOrWhiteSpace(text);
                SetTextLength(_inputField.text);
                _messageText.gameObject.SetActive(false);
            });
            _inputField.onEndEdit.AddListener(text =>
            {
                _saveButton.interactable = !string.IsNullOrWhiteSpace(text);
                _inputField.text = PartyName.Culling(text);
                SetTextLength(_inputField.text);

                // 文字数制限によりScript側から制御した場合、InputFieldの表示位置がずれることがあるため対応
                _inputField.textComponent.rectTransform.anchoredPosition = _inputFieldAnchorPosition;
                if (_inputFieldCaret == null) return;
                _inputFieldCaret.rectTransform.anchoredPosition = _inputFieldCaretAnchorPosition;
            });
        }

        IEnumerator GetInputFieldCaret()
        {
            yield return null;
            // TMP_InputFieldのtextViewportからCaretを取得する関係で生成後を待ちたい、1フレーム待機
            _inputFieldCaret = _inputField.textViewport.GetComponentInChildren<TMP_SelectionCaret>();
            if (_inputFieldCaret == null) yield break;
            _inputFieldCaretAnchorPosition = _inputFieldCaret.rectTransform.anchoredPosition;
        }

        public string InputText => _inputField.text;

        public void SetPartyName(PartyName partyName)
        {
            _inputField.text = partyName.Value;
            _inputMaxLength.SetText(PartyName.MaxLength.ToString());
            SetTextLength(partyName.Value);
        }

        void SetTextLength(string text)
        {
            _inputTextCount.SetText(text?.Length.ToString() ?? "0");
        }

        public void ShowInvalidNameMessage()
        {
            _messageText.gameObject.SetActive(true);
        }
    }
}
