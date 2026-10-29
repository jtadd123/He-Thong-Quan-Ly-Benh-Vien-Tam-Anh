@echo off
chcp 65001 >nul
cls
echo ===============================================================================
echo   HỆ THỐNG QUẢN LÝ BỆNH VIỆN ĐA KHOA TÂM ANH - TỰ ĐỘNG TEST VÀ PUSH GITHUB
echo   Tác giả: Nguyễn Văn Hưng ^| Nhánh: feature/patients-and-reception
echo ===============================================================================
echo.
echo [1/3] Đang tiến hành kiểm thử tự động toàn diện (PHPUnit / Artisan Test)...
echo -------------------------------------------------------------------------------
call php artisan test
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ===============================================================================
    echo   [CẢNH BÁO LỖI] Có kiểm thử không đạt! Đã hủy Git Push để bảo đảm an toàn.
    echo   Vui lòng sửa mã nguồn trước khi đẩy lên cho Thầy và Hội đồng kiểm tra.
    echo ===============================================================================
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo ===============================================================================
echo   [XÁC NHẬN] 100%% Test Cases Đã Xanh (Green)! Chuẩn bị đóng gói Commit...
echo ===============================================================================
echo.
echo [2/3] Đang chuẩn bị Git commit tiến độ...
echo -------------------------------------------------------------------------------
git add .
set COMMIT_MSG=feat(wave4): hoan thanh tron ven 50+ dot pha y khoa - TDM, TPN, FIM, YHCT, ESG, ALARA [232 tests passed 100% Green] - %date% %time%
git commit -m "%COMMIT_MSG%"

echo.
echo [3/3] Đang đẩy toàn bộ mã nguồn lên GitHub (origin/feature/patients-and-reception)...
echo -------------------------------------------------------------------------------
git push origin feature/patients-and-reception
if %ERRORLEVEL% EQU 0 (
    echo.
    echo ===============================================================================
    echo   [THÀNH CÔNG RỰC RỠ] ĐÃ PUSH TOÀN BỘ TIẾN ĐỘ LÊN GITHUB!
    echo.
    echo   - Nhánh: origin/feature/patients-and-reception
    echo   - Pull Request #4 trên GitHub đã tự động cập nhật mã nguồn mới nhất.
    echo   - Thầy giáo và Hội đồng có thể thẩm định trực tiếp trên GitHub:
    echo     https://github.com/quy0358/He-Thong-Quan-Ly-Benh-Vien-Tam-Anh/pull/4
    echo ===============================================================================
) else (
    echo.
    echo [LƯU Ý] Không thể đẩy lên GitHub. Vui lòng kiểm tra kết nối mạng hoặc quyền truy cập Git.
)

echo.
pause
