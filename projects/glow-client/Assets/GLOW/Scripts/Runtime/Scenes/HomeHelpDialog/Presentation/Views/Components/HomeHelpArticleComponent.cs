using System.Collections.Generic;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.HomeHelpDialog.Constants;
using GLOW.Scenes.HomeHelpDialog.Domain.ValueObjects;
using GLOW.Scenes.HomeHelpDialog.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.HomeHelpDialog.Presentation.Views.Components
{
    public class HomeHelpArticleComponent : UIObject
    {
        [SerializeField] UIText _textPrefab;
        [SerializeField] UIImage _imagePrefab;

        [SerializeField] VerticalLayoutGroup _layoutGroup;
        [SerializeField] LayoutElement _layoutElement;

        List<UIText> _texts = new List<UIText>();
        List<UIImage> _images = new List<UIImage>();
        float _alpha;
        float _maxHeight;
        IReadOnlyList<HomeHelpArticleViewModel> _articles = new List<HomeHelpArticleViewModel>();

        public float MaxHeight => _maxHeight;

        public void SetUp(IReadOnlyList<HomeHelpArticleViewModel> article)
        {
            _articles = article;

            _layoutElement.preferredHeight = article.Count * 30;
            _maxHeight = _layoutElement.preferredHeight;

            EnableLayoutGroup(false);
        }

        public void Initialize()
        {
            foreach (var viewModel in _articles)
            {
                if (HomeHelpArticleType.Image == viewModel.Type)
                {
                    AddImageObject(viewModel.Text);
                }
                else
                {
                    AddTextObject(viewModel.Text);
                }
            }
        }

        void EnableLayoutGroup(bool isEnable)
        {
            _layoutGroup.enabled = isEnable;
            _layoutElement.enabled = !isEnable;
        }

        void AddImageObject(string assetPath)
        {
            var image = Instantiate(_imagePrefab, transform);
            UISpriteUtil.LoadSpriteWithFade(image.Image, assetPath, () =>
            {
                if (!image) return;
                image.Image.SetNativeSize();

                // NOTE: このタイミングでアルファかけても一瞬表示されるので、レイアウト補正時に表示してアルファ設定する
                image.Hidden = true;
        });
            _images.Add(image);
        }

        void AddTextObject(string text)
        {
            var textObject = Instantiate(_textPrefab, transform);
            textObject.SetText(text);
            LayoutRebuilder.ForceRebuildLayoutImmediate(textObject.RectTransform);
            var color = Color.white;
            color.a = _alpha;
            textObject.SetColor(color);
            _texts.Add(textObject);
        }

        public void UpdateContentSize()
        {
            DoAsync.Invoke(this.gameObject, async cancellationToken =>
            {
                EnableLayoutGroup(true);
                foreach (var img in _images)
                {
                    img.Hidden = false;
                    img.Alpha = _alpha;
                }

                await UniTask.DelayFrame(1, cancellationToken: cancellationToken);

                _layoutGroup.CalculateLayoutInputVertical();
                LayoutRebuilder.ForceRebuildLayoutImmediate(this.RectTransform);
                _layoutElement.preferredHeight = this.RectTransform.sizeDelta.y;
                _maxHeight = _layoutElement.preferredHeight;

                EnableLayoutGroup(false);
            });
        }

        public void SetHeightRate(float rate)
        {
            _layoutElement.preferredHeight = rate * _maxHeight;
        }

        public void SetAlpha(float alpha)
        {
            _alpha = alpha;
            foreach (var text in _texts)
            {
                var color = Color.white;
                color.a = alpha;
                text.SetColor(color);
            }

            foreach (var image in _images)
            {
                var color = image.Color;
                color.a = alpha;
                image.Color = color;
            }
        }
    }
}
