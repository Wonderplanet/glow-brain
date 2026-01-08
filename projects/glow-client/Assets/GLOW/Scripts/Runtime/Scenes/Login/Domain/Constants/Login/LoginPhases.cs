namespace GLOW.Scenes.Login.Domain.Constants.Login
{
    public enum LoginPhases
    {
        None,
        LicenseAgreement,
        FetchServerTime,
        FetchGameVersion,
        Authenticate,
        SDKInitialize,
        FetchMstDataManifest,
        FetchMstData,
        LoadMstData,
        FetchAssetBundleManifest,
        FetchAssetBundle,
        FetchUserData,
        Complete,
        TransitionToHome
    }
}
