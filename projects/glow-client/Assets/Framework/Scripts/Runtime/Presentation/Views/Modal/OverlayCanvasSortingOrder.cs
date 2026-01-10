namespace WPFramework.Presentation.Views
{
    // TODO: Loadingシーンなどの兼ね合いや、PostEffect化を踏まえて検討
    public enum OverlayCanvasSortingOrder
    {
        Camera = 0,
        BlurScreen = 100,
        Modal = 300,
        LoadingTransition = 400,      // Loading系のシーンのCanvasに直接指定
        SystemCanvas = 999,           // notificationなど
        SystemCanvasModal = 1000,     // エラーSystemモーダルなど
        TutorialGlayout = 1030,       // チュートリアル用のグレイアウト
        UIHighlight = 1040,           // UIをハイライトするためのCanvas位置
        TutorialUI = 1050,              // チュートリアル用のCanvas位置
        TutorialInvisibleButton = 1060, // チュートリアル用の透明ボタン
        TutorialSkipButton = 1070,      // チュートリアル用のスキップボタン
        GeneralMaskTransition = 1100, // Transition系のPrefabに直接指定
        RebootMask = 2000             // プレファブに直接指定
    }
}
