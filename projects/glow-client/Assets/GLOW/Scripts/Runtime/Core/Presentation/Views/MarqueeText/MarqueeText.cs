using UnityEngine;
using TMPro;
using UnityEngine.UI;
using System.Collections;

public class MarqueeText : MonoBehaviour
{
    public float scrollSpeed = 100f;
    public float waitAfterShow = 1f;
    public float fadeTime = 1f;
    public float startStop = 1f;

    TMP_Text tmpText;
    RectTransform rectTransform;
    RectTransform parentRect;
    CanvasGroup canvasGroup;
    float startPosX;
    float endPosX;
    float textWidth;
    float maskWidth;

    Coroutine marqueeCoroutine;
    float prevTextWidth = -1f;
    float prevMaskWidth = -1f;
    string prevText = "";

    void Start()
    {
        tmpText = GetComponent<TMP_Text>();
        rectTransform = GetComponent<RectTransform>();
        parentRect = rectTransform.parent.GetComponent<RectTransform>();
        canvasGroup = GetComponent<CanvasGroup>();
        if (canvasGroup == null) canvasGroup = gameObject.AddComponent<CanvasGroup>();

        StartCoroutine(AutoRefreshRoutine());
        Refresh();
    }

    /// <summary>
    /// 外部からテキスト変更後などにも呼べる
    /// </summary>
    public void Refresh()
    {
        // すでに動いてたら止める
        if (marqueeCoroutine != null)
        {
            StopCoroutine(marqueeCoroutine);
            marqueeCoroutine = null;
        }
        marqueeCoroutine = StartCoroutine(MarqueeRoutine());
    }

    /// <summary>
    /// テキストやサイズの変化を自動検知
    /// </summary>
    IEnumerator AutoRefreshRoutine()
    {
        while (true)
        {
            // 毎フレームチェック（負荷が気になるなら0.2秒間隔なども可）
            LayoutRebuilder.ForceRebuildLayoutImmediate(rectTransform);
            float curTextWidth = tmpText.preferredWidth;
            float curMaskWidth = parentRect.rect.width;
            string curText = tmpText.text;
            // サイズまたはテキスト内容が変わったら強制リフレッシュ
            if (curTextWidth != prevTextWidth || curMaskWidth != prevMaskWidth || curText != prevText)
            {
                prevTextWidth = curTextWidth;
                prevMaskWidth = curMaskWidth;
                prevText = curText;
                Refresh();
            }
            yield return null; // 負荷が気になるなら yield return new WaitForSeconds(0.1f);
        }
    }

    IEnumerator MarqueeRoutine()
    {
        // 幅計算
        LayoutRebuilder.ForceRebuildLayoutImmediate(rectTransform);
        textWidth = tmpText.preferredWidth;
        maskWidth = parentRect.rect.width;
        startPosX = rectTransform.anchoredPosition.x;
        endPosX = startPosX - (textWidth - maskWidth);

        // 親より子が小さい or 同じ場合はアニメなしで終了
        if (textWidth <= maskWidth)
        {
            canvasGroup.alpha = 1;
            rectTransform.anchoredPosition = new Vector2(startPosX, rectTransform.anchoredPosition.y);
            yield break;
        }

        while (true)
        {
            // フェードイン
            yield return StartCoroutine(FadeIn());

            // 完全停止
            yield return new WaitForSeconds(startStop);

            // スクロール開始
            float posX = startPosX;
            bool waitStarted = false;
            float waitTimer = 0f;

            while (true)
            {
                posX -= scrollSpeed * Time.deltaTime;
                rectTransform.anchoredPosition = new Vector2(posX, rectTransform.anchoredPosition.y);

                // 全文表示後のX秒待機
                if (!waitStarted && posX <= endPosX)
                {
                    waitStarted = true;
                    waitTimer = 0f;
                }
                if (waitStarted)
                {
                    waitTimer += Time.deltaTime;
                    if (waitTimer >= waitAfterShow)
                        break;
                }
                yield return null;
            }

            // フェードアウトしながらスクロール
            yield return StartCoroutine(FadeOutWhileScroll(posX));

            // 完全消えたら元の位置に即戻す
            rectTransform.anchoredPosition = new Vector2(startPosX, rectTransform.anchoredPosition.y);
        }
    }

    IEnumerator FadeIn()
    {
        float t = 0f;
        while (t < fadeTime)
        {
            canvasGroup.alpha = Mathf.Lerp(0, 1, t / fadeTime);
            t += Time.deltaTime;
            yield return null;
        }
        canvasGroup.alpha = 1;
    }

    IEnumerator FadeOutWhileScroll(float startPosX)
    {
        float t = 0f;
        float posX = startPosX;
        while (t < fadeTime)
        {
            canvasGroup.alpha = Mathf.Lerp(1, 0, t / fadeTime);
            // スクロールは止めない
            posX -= scrollSpeed * Time.deltaTime;
            rectTransform.anchoredPosition = new Vector2(posX, rectTransform.anchoredPosition.y);
            t += Time.deltaTime;
            yield return null;
        }
        canvasGroup.alpha = 0;
    }
}
