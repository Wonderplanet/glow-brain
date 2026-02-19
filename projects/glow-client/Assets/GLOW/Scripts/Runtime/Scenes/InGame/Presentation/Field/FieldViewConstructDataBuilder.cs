using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    /// <summary>
    /// FieldViewConstructDataを作成するためのクラス
    /// </summary>
    public class FieldViewConstructDataBuilder
    {
        MstPageModel _mstPageModel;
        float _pageComponentWidth;

        public FieldViewConstructDataBuilder SetPageModel(MstPageModel mstPageModel)
        {
            _mstPageModel = mstPageModel;
            return this;
        }

        public FieldViewConstructDataBuilder SetPageComponentWidth(float pageComponentWidth)
        {
            _pageComponentWidth = pageComponentWidth;
            return this;
        }

        public FieldViewConstructData Build()
        {
            var fieldViewPixelWidth = _mstPageModel.TotalWidth * _pageComponentWidth;

            // 必殺ワザ時はコマの高さより上にはみ出た部分も表示する必要があるので高めにしておく。
            // 大体はコマ一行分の幅（_pageComponentWidth）よりコマの高さの方が低いので、コマ一行分の幅をfieldViewPixelHeightにしておく。
            // コマ一行分の幅よりコマの高さがすごく高い場合は、コマの高さよりfieldViewPixelHeightが低くなるが、
            // あまりfieldViewPixelHeightを高くしてもパフォーマンスが悪くなりそうなので妥協する
            var fieldViewPixelHeight = _pageComponentWidth;

            var fieldViewPixelSize = new Vector2(fieldViewPixelWidth, fieldViewPixelHeight);

            var fieldViewRect = CreateFieldViewRect(fieldViewPixelSize);
            var fieldViewOriginPoint = new Vector2(fieldViewRect.xMax, fieldViewRect.yMin);

            float tierViewWidth = _pageComponentWidth * 0.01f;
            var komaAreaDictionary = CreateKomaAreaDictionary(fieldViewRect);

            return new FieldViewConstructData(
                fieldViewPixelSize,
                fieldViewRect,
                fieldViewOriginPoint,
                tierViewWidth,
                komaAreaDictionary);
        }

        Rect CreateFieldViewRect(Vector2 fieldViewPixelSize)
        {
            Vector2 halfFieldViewSize = fieldViewPixelSize * (0.01f * 0.5f);

            var fieldViewRect = new Rect();
            fieldViewRect.xMin = -halfFieldViewSize.x;
            fieldViewRect.xMax = halfFieldViewSize.x;
            fieldViewRect.yMin = -halfFieldViewSize.y;
            fieldViewRect.yMax = halfFieldViewSize.y;

            return fieldViewRect;
        }

        IReadOnlyDictionary<KomaId, Rect> CreateKomaAreaDictionary(Rect fieldViewRect)
        {
            float totalWidth = _mstPageModel.TotalWidth;

            var komaAreaDictionary = new Dictionary<KomaId, Rect>();
            float komaLeftPos = 0f;

            foreach (var komaLine in _mstPageModel.KomaLineList)
            {
                foreach (MstKomaModel koma in komaLine.KomaList)
                {
                    komaLeftPos += koma.Width;

                    var rect = new Rect();
                    rect.xMin = fieldViewRect.xMax - komaLeftPos / totalWidth * fieldViewRect.width;
                    rect.xMax = fieldViewRect.xMax - (komaLeftPos - koma.Width) / totalWidth * fieldViewRect.width;
                    rect.yMin = fieldViewRect.yMin;
                    rect.yMax = fieldViewRect.yMin + komaLine.Height / totalWidth * fieldViewRect.width;

                    komaAreaDictionary.Add(koma.KomaId, rect);
                }
            }

            return komaAreaDictionary;
        }
    }
}
