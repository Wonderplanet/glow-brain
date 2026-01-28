using System.Collections;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.CustomCarousel;
using GLOW.Scenes.Home.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.QuestSelect.Presentation
{
    public class QuestSelectCell : GlowCustomInfiniteCarouselCell
    {
        [Header("クエスト情報")]
        [SerializeField] HomeMainQuestSymbolImage _symbolImage;
        [SerializeField] QuestDifficultyLabelComponent _difficultyLabelComponent;
        [Header("Newアイコン")]
        [SerializeField] GameObject _newIconObj;
        [Header("ボタン押し選択")]
        [SerializeField] Button _selectButton;
        [SerializeField] Image _selectButtonImage;
        [SerializeField] GameObject _selectAnimationObj;

        [Header("未開放オブジェクト")]
        [SerializeField] GameObject _notReleaseObject;
        [SerializeField] Image _notReleaseImage;
        [SerializeField] UIText _releaseRequireText;

        public HomeMainQuestSymbolImage SymbolImage => _symbolImage;
        public QuestDifficultyLabelComponent DifficultyLabelComponent => _difficultyLabelComponent;
        public GameObject NewIconObj => _newIconObj;
        public Button SelectButton => _selectButton;
        public GameObject NotReleaseObject => _notReleaseObject;
        public UIText ReleaseRequireText => _releaseRequireText;
        // チュートリアル用 開いた時に選択されていたか
        public bool IsInitialSelected { get; set; }

        protected override void Awake()
        {
            base.Awake();
            AddButton(_selectButton, "select");
        }

        public IEnumerator StartSelectAnimation()
        {
            _selectAnimationObj.SetActive(true);
            yield return new WaitForSeconds(0.4f);
        }

        public void OnUpdateButtonStatus(QuestOpenStatus modelOpenStatus, int centerIndex)
        {
            var isActive = modelOpenStatus == QuestOpenStatus.Released && Index == centerIndex;
            _selectButton.interactable = isActive;
            _selectButtonImage.raycastTarget = isActive;

            _notReleaseImage.raycastTarget = modelOpenStatus != QuestOpenStatus.Released && Index == centerIndex;
        }
    }
}
