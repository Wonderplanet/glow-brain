using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using TMPro;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.AgeConfirm.Presentation.View
{
    /// <summary>
    /// 74-1_年齢確認
    /// </summary>
    public class AgeConfirmationDialogView : UIView
    {
        [SerializeField] TMP_InputField _inputField;
        [SerializeField] UIText _inputTextCount;
        [SerializeField] Button _okButton;
        [SerializeField] UIImage _buttonGrayOutImage;

        public string InputText => _inputField.text;

        protected override void Awake()
        {
            base.Awake();
            _okButton.interactable = _inputField.text.Length == DateOfBirth.MaxLength;
            _buttonGrayOutImage.Hidden = !_okButton.interactable;
            _inputField.contentType = TMP_InputField.ContentType.IntegerNumber;

            _inputField.onValueChanged.AddListener(text =>
            {
                _inputField.text = DateOfBirth.Culling(text);
                SetTextLength(_inputField.text);

                // 8桁になったらボタンを押せるようにする
                _okButton.interactable = _inputField.text.Length == DateOfBirth.MaxLength;
                _buttonGrayOutImage.Hidden = !_okButton.interactable;

                // 数値以外の文字が入力されたら削除
                ValidateInput(_inputField.text);
            });
        }

        void SetTextLength(string text)
        {
            _inputTextCount.SetText(text?.Length.ToString() ?? "0");
        }

        void ValidateInput(string input)
        {
            // 数値以外の文字が含まれているか確認
            for (int i = 0; i < input.Length; i++)
            {
                if (!char.IsDigit(input[i]))
                {
                    // 数値以外の文字が入力されたら削除
                    _inputField.text = input.Remove(i, 1);
                    return;
                }
            }
        }
    }
}
