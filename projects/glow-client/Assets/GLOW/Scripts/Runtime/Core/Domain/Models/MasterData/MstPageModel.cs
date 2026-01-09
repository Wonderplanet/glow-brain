using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Core.Domain.Models
{
    public record MstPageModel(MasterDataId MstPageId,IReadOnlyList<MstKomaLineModel> KomaLineList)
    {
        public static MstPageModel Empty { get; } = new(MasterDataId.Empty, Array.Empty<MstKomaLineModel>());

        List<MstKomaModel> _komaList;

        public float PageWidth => 1f;

        public float TotalWidth => KomaLineList
            .SelectMany(line => line.KomaList.Select(koma => koma.Width))
            .Sum();

        public IReadOnlyList<float> KomaLineHeightList => KomaLineList.Select(line => line.Height).ToList();

        public float MaxKomaLineHeight => KomaLineList.Select(line => line.Height).Max();

        public IReadOnlyList<MstKomaModel> KomaList
        {
            get
            {
                if (_komaList == null)
                {
                    _komaList = KomaLineList
                        .SelectMany(line => line.KomaList)
                        .ToList();
                }

                return _komaList;
            }
        }

        public int KomaCount => _komaList.Count;
        public int KomaLineCount => KomaLineList.Count;

        public KomaNo MaxKomaNo => new KomaNo(KomaList.Count - 1);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public KomaNo GetKomaNoAt(FieldCoordV2 pos)
        {
            if (pos.X < 0) return KomaNo.Empty;

            var width = 0f;

            for (int i = 0; i < KomaList.Count; i++)
            {
                width += KomaList[i].Width;
                if (width >= pos.X)
                {
                    return new KomaNo(i);
                }
            }

            return KomaNo.Empty;
        }

        public KomaId GetKomaIdAt(FieldCoordV2 pos)
        {
            var komaNo = GetKomaNoAt(pos);
            var koma = GetKoma(komaNo);
            return koma.KomaId;
        }

        public CoordinateRange GetKomaRange(KomaNo komaNo)
        {
            if (komaNo.IsEmpty() || komaNo < KomaNo.Zero || komaNo > MaxKomaNo)
            {
                return CoordinateRange.Empty;
            }

            var min = KomaList.Take(komaNo.Value).Sum(koma => koma.Width);
            var max = min + KomaList[komaNo.Value].Width;

            return new CoordinateRange(min, max);
        }

        public CoordinateRange GetKomaRange(KomaId komaId)
        {
            var komaNo = GetKomaNo(komaId);
            return GetKomaRange(komaNo);
        }

        public bool ExistsKomaLine(KomaLineNo komaLineNo)
        {
            return komaLineNo.Value >= 0 && komaLineNo.Value < KomaLineList.Count;
        }

        public KomaNo GetKomaNo(KomaId komaId)
        {
            for (int i = 0; i < KomaList.Count; i++)
            {
                if (KomaList[i].KomaId == komaId)
                {
                    return new KomaNo(i);
                }
            }
            return KomaNo.Empty;
        }

        public MstKomaLineModel GetKomaLine(KomaId komaId)
        {
            foreach (var komaLine in KomaLineList)
            {
                if (komaLine.KomaList.Any(koma => koma.KomaId == komaId))
                {
                    return komaLine;
                }
            }
            return MstKomaLineModel.Empty;
        }

        public KomaLineNo GetKomaLineNo(KomaId komaId)
        {
            for (int i = 0; i < KomaLineList.Count; i++)
            {
                if (KomaLineList[i].KomaList.Any(koma => koma.KomaId == komaId))
                {
                    return new KomaLineNo(i);
                }
            }
            return KomaLineNo.Empty;
        }

        public KomaLineNo GetKomaLineNoAt(FieldCoordV2 pos)
        {
            int lineNo = Mathf.FloorToInt(pos.X / PageWidth);

            if (lineNo < 0 || lineNo >= KomaLineList.Count)
            {
                return KomaLineNo.Empty;
            }
            return new KomaLineNo(lineNo);
        }

        public CoordinateRange GetKomaLineRange(KomaLineNo komaLineNo)
        {
            if (komaLineNo.IsEmpty() || komaLineNo.Value < 0 || komaLineNo.Value >= KomaList.Count)
            {
                return CoordinateRange.Empty;
            }

            return new CoordinateRange(
                komaLineNo.Value * PageWidth,
                (komaLineNo.Value + 1) * PageWidth);
        }

        public (float KomaWidth, FieldCoordV2 PageCoord) GetKomaWidthAndFrontPosAtPos(FieldCoordV2 pos)
        {
            float komaWidth = 0.0f;
            FieldCoordV2 pageCoord = pos;

            int lineNo = Mathf.FloorToInt(pos.X / PageWidth);
            var komaLineModel = KomaLineList[lineNo];
            var linePos = pos.X - lineNo;

            float addWidth = 0.0f;
            foreach (var koma in komaLineModel.KomaList)
            {
                addWidth += koma.Width;
                if (addWidth >= linePos)
                {
                    komaWidth = koma.Width;
                    pageCoord = new FieldCoordV2(addWidth - koma.Width + lineNo, pos.Y);
                    break;
                }
            }

            return (komaWidth, pageCoord);
        }

        public float GetKomaHeight(KomaNo komaNo)
        {
            var koma = GetKoma(komaNo);
            var komaLineNo = GetKomaLineNo(koma.KomaId);
            if (komaLineNo.IsEmpty() || komaLineNo.Value < 0 || komaLineNo.Value >= KomaLineHeightList.Count)
            {
                return 0f;
            }

            return KomaLineHeightList[komaLineNo.Value];
        }

        public FieldCoordV2 ClampByPage(FieldCoordV2 pos)
        {
            return new FieldCoordV2(Mathf.Clamp(pos.X, 0, TotalWidth), pos.Y);
        }

        public MstKomaModel GetKoma(KomaNo komaNo)
        {
            if (komaNo.IsEmpty() || komaNo.Value < 0 || komaNo.Value >= KomaList.Count)
            {
                return MstKomaModel.Empty;
            }

            return KomaList[komaNo.Value];
        }
    }
}
