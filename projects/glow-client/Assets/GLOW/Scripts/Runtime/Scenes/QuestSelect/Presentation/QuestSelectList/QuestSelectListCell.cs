using System.Collections;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.Components;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.QuestSelect.Presentation
{
    public class QuestSelectListCell : UICollectionViewCell
    {
        [Header("ボタン情報")]
        [SerializeField] Button _questSelectButton;
        [SerializeField] CanvasGroup _rootAreaCanvasGroup;
        [Header("クエスト情報")]
        [SerializeField] HomeMainQuestSymbolImage _symbolImage;
        [Header("Newアイコン")]
        [SerializeField] GameObject _newIconObj;
        [Header("選択中アイコン")]
        [SerializeField] GameObject _selectedIconObj;
        [Header("ボタン押し選択")]
        [SerializeField] Image _selectButtonImage;
        [Header("未開放オブジェクト")]
        [SerializeField] GameObject _notReleaseObject;
        [SerializeField] Image _notReleaseImage;
        [SerializeField] UIText _releaseRequireText;
        [Header("原画のかけら数(獲得数/獲得可能数")]
        [SerializeField] UIObject _artworkFragmentObject;
        [SerializeField] UIText _artworkFragmentText;

        // チュートリアル用 開いた時に選択されていたか
        public bool IsInitialSelected { get; set; }
        // 先頭のセルか？
        public bool IsListFirstCell { get; set; }
        // 2番目のセルか？
        public bool IsListSecondCell { get; set; }

        const string ArtworkFragmentTextFormat = "{0} / {1}";

        public void Setup(
            QuestSelectContentViewModel model,
            MasterDataId initialSelectedMstQuestId,
            bool isListFirstCell,
            bool isListSecondCell)
        {
            _symbolImage.AssetPath = model.AssetPath.Value;
            _notReleaseObject.SetActive(model.OpenStatus != QuestOpenStatus.Released);
            _newIconObj.SetActive(model.NewQuestExists);
            _releaseRequireText.SetText(model.RequiredSentence.Value);

            // 選択中
            var isInitializeSelected = model.QuestDifficultyItemViewModels.Any(m => m.MstQuestId == initialSelectedMstQuestId);
            _selectedIconObj.SetActive(isInitializeSelected);
            // チュートリアル向け設定
            IsInitialSelected = isInitializeSelected;
            IsListFirstCell = isListFirstCell;
            IsListSecondCell = isListSecondCell;

            // 開放状態のときは未解放ボタン領域のRaycast Off
            _rootAreaCanvasGroup.blocksRaycasts = model.OpenStatus != QuestOpenStatus.Released;
            // 開放 and 未選択中cellのとき、選択ボタン押せるように
            _questSelectButton.interactable = model.OpenStatus == QuestOpenStatus.Released && !isInitializeSelected;

            // 原画のかけら
            var gettable = model.GetGettableArtworkFragmentNum();
            var acquired = model.GetAcquiredArtworkFragmentNum();
            var isGettable = !gettable.IsZero();
            _artworkFragmentObject.Hidden = !isGettable || model.OpenStatus != QuestOpenStatus.Released;
            if (isGettable)
            {
                _artworkFragmentText.SetText(ZString.Format(ArtworkFragmentTextFormat, acquired.Value, gettable.Value));
            }
        }
    }
}
