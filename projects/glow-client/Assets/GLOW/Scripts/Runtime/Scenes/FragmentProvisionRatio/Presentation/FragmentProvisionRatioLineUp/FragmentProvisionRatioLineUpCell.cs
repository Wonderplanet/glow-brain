using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation.FragmentProvisionRatioLineUp
{
    public class FragmentProvisionRatioLineUpCell: MonoBehaviour
    {
        [Header("背景")]
        [SerializeField] Image _whiteBackGround;
        [SerializeField] Image _grayBackGround;
        [Header("アイコンボタン")]
        [SerializeField] Button iconButton;
        [Header("ユニットアイコン")]
        [SerializeField] CharacterIconComponent _characterIconComponent;
        [Header("アイテムアイコン")]
        [SerializeField] ItemIconComponent _itemIconComponent;
        [Header("アイテム情報")]
        [SerializeField] UIText _nameText;
        [SerializeField] UIText _ratio;

        public Image WhiteBackGround => _whiteBackGround;
        public Image GrayBackGround => _grayBackGround;

        public Button IconButton => iconButton;

        public CharacterIconComponent CharacterIconComponent => _characterIconComponent;
        public ItemIconComponent ItemIconComponent => _itemIconComponent;
        public UIText NameText => _nameText;
        public UIText Ratio => _ratio;
    }

}
