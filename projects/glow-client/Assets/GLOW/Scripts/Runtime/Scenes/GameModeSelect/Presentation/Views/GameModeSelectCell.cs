using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GameModeSelect.Domain;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.GameModeSelect.Presentation
{
    public class GameModeSelectCell : UICollectionViewCell
    {
        [SerializeField] UIImage _eventModeImage;
        [SerializeField] UIImage _selectedImage;
        [Header("時間")]
        [SerializeField] GameObject _timeRoot;
        [SerializeField] UIText _limitTimeText;
        [Header("汎用ボタンイメージ")]
        [SerializeField] Sprite _mainQuestSprite;

        // チュートリアル用 いいジャン祭セル確認用
        public GameModeType GameModeType { get; set; }
        public bool IsEventMode => GameModeType == GameModeType.Event;
        public UIImage EventModeImage => _eventModeImage;
        public UIImage SelectedImage => _selectedImage;
        public GameObject TimeRoot => _timeRoot;
        public UIText LimitTimeText => _limitTimeText;
        public Sprite MainQuestSprite => _mainQuestSprite;
    }
}
