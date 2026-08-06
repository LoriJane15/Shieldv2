<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Afp;
use App\Http\Controllers\DilgReviewer\EclipReviewController;
use App\Http\Controllers\EclipAnalyticsController;
use App\Http\Controllers\EclipBasicServiceDocumentController;
use App\Http\Controllers\EclipAssessor;
use App\Http\Controllers\EclipDocumentDownloadController;
use App\Http\Controllers\EclipFunding\FundingController;
use App\Http\Controllers\GovAgency;
use App\Http\Controllers\Ib39;
use App\Http\Controllers\Japic;
use App\Http\Controllers\Lgu;
use App\Http\Controllers\LocalEclip\ReleaseController;
use App\Http\Controllers\Lswdo;
use App\Http\Controllers\Mblrc;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Pnp;
use App\Http\Controllers\SuperAdmin;
use Illuminate\Support\Facades\Route;

// Public landing page — original static site copied to public/landing/.
Route::get('/', fn () => response()->file(public_path('landing/index.html'), [
    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    'Pragma' => 'no-cache',
    'Expires' => '0',
]))->name('landing');

// Shared authenticated routes.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/status', [NotificationController::class, 'status'])->middleware('throttle:120,1')->name('notifications.status');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/eclip-analytics', [EclipAnalyticsController::class, 'index'])->name('eclip.analytics.index');
    Route::get('/eclip-analytics/export', [EclipAnalyticsController::class, 'export'])->name('eclip.analytics.export');
});

/*
| Role areas. Each group mirrors one legacy accounts/<role>/ folder and is
| gated by the `role` middleware. Feature routes are added per role during
| the phased migration; for now each exposes a dashboard.
*/

Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/', [SuperAdmin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [SuperAdmin\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [SuperAdmin\UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [SuperAdmin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [SuperAdmin\UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/agencies', [SuperAdmin\AgencyController::class, 'index'])->name('agencies.index');
    Route::post('/agencies', [SuperAdmin\AgencyController::class, 'store'])->name('agencies.store');
    Route::put('/agencies/{agency}', [SuperAdmin\AgencyController::class, 'update'])->name('agencies.update');
    Route::delete('/agencies/{agency}', [SuperAdmin\AgencyController::class, 'destroy'])->name('agencies.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('katuparan')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // RCSP form review
    Route::get('/rcsp', [Admin\RcspReviewController::class, 'index'])->name('rcsp.index');
    Route::get('/rcsp/{rcspBarangay}', [Admin\RcspReviewController::class, 'show'])->name('rcsp.show');
    Route::post('/rcsp/{rcspBarangay}/review', [Admin\RcspReviewController::class, 'updateStatus'])->name('rcsp.review');
    Route::get('/rcsp-form/{form}/file', [Admin\RcspReviewController::class, 'file'])->name('rcsp.file');
    Route::post('/rcsp-form/{form}/comment', [Admin\RcspReviewController::class, 'storeComment'])->name('rcsp.comment');

    // IMPLAN verify + reassign
    Route::get('/implan', [Admin\ImplanController::class, 'index'])->name('implan.index');
    Route::get('/implan/{implan}', [Admin\ImplanController::class, 'show'])->name('implan.show');
    Route::post('/implan/{implan}/verify', [Admin\ImplanController::class, 'verify'])->name('implan.verify');
    Route::post('/implan/{implan}/reassign', [Admin\ImplanController::class, 'reassign'])->name('implan.reassign');

    Route::get('/eclip-document-requirements', [Admin\EclipDocumentRequirementController::class, 'index'])->name('eclip.requirements.index');
    Route::post('/eclip-document-requirements', [Admin\EclipDocumentRequirementController::class, 'store'])->name('eclip.requirements.store');
    Route::patch('/eclip-document-requirements/{requirement}/toggle', [Admin\EclipDocumentRequirementController::class, 'toggle'])->name('eclip.requirements.toggle');
    Route::get('/eclip-assistance-categories', [Admin\EclipAssistanceCategoryController::class, 'index'])->name('eclip.assistance-categories.index');
    Route::post('/eclip-assistance-categories', [Admin\EclipAssistanceCategoryController::class, 'store'])->name('eclip.assistance-categories.store');
    Route::patch('/eclip-assistance-categories/{category}/toggle', [Admin\EclipAssistanceCategoryController::class, 'toggle'])->name('eclip.assistance-categories.toggle');

    // Overviews
    Route::get('/agencies', [Admin\OverviewController::class, 'agencies'])->name('agencies.index');
    Route::get('/locations', [Admin\OverviewController::class, 'locations'])->name('locations.index');
    Route::get('/clusters', [Admin\OverviewController::class, 'clusters'])->name('clusters.index');
    Route::get('/clusters/{slug}', [Admin\OverviewController::class, 'clusterProfile'])->name('clusters.show');
    Route::get('/users', [Admin\OverviewController::class, 'users'])->name('users.index');
});

Route::middleware(['auth', 'role:lgu'])->prefix('lgu')->name('lgu.')->group(function () {
    Route::get('/', [Lgu\DashboardController::class, 'index'])->name('dashboard');

    // RCSP barangays
    Route::get('/rcsp', [Lgu\RcspBarangayController::class, 'index'])->name('rcsp.index');
    Route::post('/rcsp', [Lgu\RcspBarangayController::class, 'store'])->name('rcsp.store');
    Route::delete('/rcsp/{rcspBarangay}', [Lgu\RcspBarangayController::class, 'destroy'])->name('rcsp.destroy');

    // Monitoring form (per barangay)
    Route::get('/rcsp/{rcspBarangay}/monitoring', [Lgu\MonitoringController::class, 'show'])->name('monitoring.show');
    Route::post('/rcsp/{rcspBarangay}/monitoring', [Lgu\MonitoringController::class, 'submit'])->name('monitoring.submit');
    Route::post('/rcsp/{rcspBarangay}/proceed', [Lgu\MonitoringController::class, 'proceed'])->name('monitoring.proceed');
    Route::get('/rcsp-form/{form}/file', [Lgu\MonitoringController::class, 'file'])->name('monitoring.file');
    Route::post('/rcsp-form/{form}/comment', [Lgu\MonitoringController::class, 'storeComment'])->name('monitoring.comment');

    // Evaluation status (read-only rollup)
    Route::get('/evaluation', [Lgu\DashboardController::class, 'index'])->name('evaluation.index');

    // IMPLAN
    Route::get('/implan', [Lgu\ImplanController::class, 'index'])->name('implan.index');
    Route::post('/implan', [Lgu\ImplanController::class, 'store'])->name('implan.store');
    Route::get('/implan/{implan}', [Lgu\ImplanController::class, 'show'])->name('implan.show');
    Route::put('/implan/{implan}', [Lgu\ImplanController::class, 'update'])->name('implan.update');
    Route::put('/implan/{implan}/implementation', [Lgu\ImplanController::class, 'updateImplementation'])->name('implan.implementation');
    Route::post('/implan/{implan}/agenda', [Lgu\ImplanController::class, 'uploadAgenda'])->name('implan.agenda');
    Route::post('/implan/{implan}/verify', [Lgu\ImplanController::class, 'verify'])->name('implan.verify');
    Route::delete('/implan/{implan}', [Lgu\ImplanController::class, 'destroy'])->name('implan.destroy');
});

Route::middleware(['auth', 'role:gov_agency'])->prefix('agency')->name('gov_agency.')->group(function () {
    Route::get('/', [GovAgency\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/implan', [GovAgency\ImplanController::class, 'index'])->name('implan.index');
    Route::get('/implan/{implan}', [GovAgency\ImplanController::class, 'show'])->name('implan.show');
    Route::post('/implan/{implan}/respond', [GovAgency\ImplanController::class, 'respond'])->name('implan.respond');
    Route::put('/implan/{implan}', [GovAgency\ImplanController::class, 'update'])->name('implan.update');
    Route::post('/implan/{implan}/agenda', [GovAgency\ImplanController::class, 'uploadAgenda'])->name('implan.agenda');
    Route::post('/implan/{implan}/photos', [GovAgency\ImplanController::class, 'uploadPhoto'])->name('implan.photos');
    Route::get('/eclip-basic-services', [GovAgency\EclipBasicServiceController::class, 'index'])->name('eclip.basic-services.index');
    Route::put('/eclip-basic-services/{basicService}', [GovAgency\EclipBasicServiceController::class, 'update'])->name('eclip.basic-services.update');
    Route::post('/eclip-basic-services/{basicService}/documents', [EclipBasicServiceDocumentController::class, 'store'])->name('eclip.basic-services.documents.store');
    Route::get('/eclip-basic-service-documents/{document}', [EclipBasicServiceDocumentController::class, 'download'])->name('eclip.basic-services.documents.download');
});

Route::middleware(['auth', 'role:mblrc'])->prefix('mblrc')->name('mblrc.')->group(function () {
    Route::get('/', [Mblrc\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [Mblrc\DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/statistics', [Mblrc\DashboardController::class, 'statistics'])->name('statistics');
    Route::get('/fr-locations', [Mblrc\FormerRebelController::class, 'locations'])->name('fr.locations');
    Route::get('/barangays', [Mblrc\FormerRebelController::class, 'barangays'])->name('barangays');
    Route::get('/skills-suggestions', [Mblrc\ProfileActionController::class, 'skillSuggestions'])->name('fr.skills.suggestions');

    Route::get('/former-rebels', [Mblrc\FormerRebelController::class, 'index'])->name('fr.index');
    Route::get('/former-rebels/create', [Mblrc\FormerRebelController::class, 'create'])->name('fr.create');
    Route::post('/former-rebels', [Mblrc\FormerRebelController::class, 'store'])->name('fr.store');
    Route::get('/former-rebels/{formerRebel}', [Mblrc\FormerRebelController::class, 'show'])->name('fr.show');
    Route::get('/former-rebels/{formerRebel}/edit', [Mblrc\FormerRebelController::class, 'edit'])->name('fr.edit');
    Route::put('/former-rebels/{formerRebel}', [Mblrc\FormerRebelController::class, 'update'])->name('fr.update');
    Route::delete('/former-rebels/{formerRebel}', [Mblrc\FormerRebelController::class, 'destroy'])->name('fr.destroy');

    // Profile widget actions
    Route::put('/former-rebels/{formerRebel}/program-status', [Mblrc\ProfileActionController::class, 'updateProgramStatus'])->name('fr.program-status.update');
    Route::post('/former-rebels/{formerRebel}/location', [Mblrc\ProfileActionController::class, 'saveLocation'])->name('fr.location.save');
    Route::get('/former-rebels/{formerRebel}/location-history', [Mblrc\ProfileActionController::class, 'locationHistory'])->name('fr.location.history');
    Route::post('/former-rebels/{formerRebel}/skills', [Mblrc\ProfileActionController::class, 'storeSkill'])->name('fr.skills.store');
    Route::delete('/skills/{skill}', [Mblrc\ProfileActionController::class, 'destroySkill'])->name('fr.skills.destroy');
    Route::post('/former-rebels/{formerRebel}/assistance', [Mblrc\ProfileActionController::class, 'storeAssistance'])->name('fr.assistance.store');
    Route::get('/assistance/{assistance}/certificate', [Mblrc\ProfileActionController::class, 'downloadAssistanceCertificate'])->name('fr.assistance.certificate');
    Route::delete('/assistance/{assistance}', [Mblrc\ProfileActionController::class, 'destroyAssistance'])->name('fr.assistance.destroy');
    Route::post('/former-rebels/{formerRebel}/education-work', [Mblrc\ProfileActionController::class, 'updateEducationWork'])->name('fr.education.update');

    Route::get('/eclip', [Mblrc\EclipCaseController::class, 'index'])->name('eclip.index');
    Route::get('/eclip/create', [Mblrc\EclipCaseController::class, 'create'])->name('eclip.create');
    Route::post('/eclip', [Mblrc\EclipCaseController::class, 'store'])->name('eclip.store');
    Route::get('/eclip/{eclipCase}', [Mblrc\EclipCaseController::class, 'show'])->name('eclip.show');
    Route::post('/eclip/{eclipCase}/submit', [Mblrc\EclipCaseController::class, 'submit'])->name('eclip.submit');
    Route::post('/eclip/{eclipCase}/documents', [Mblrc\EclipDocumentController::class, 'store'])->name('eclip.documents.store');
    Route::get('/eclip-document-versions/{version}', EclipDocumentDownloadController::class)->name('eclip.documents.download');
});

Route::middleware(['auth', 'role:lswdo'])->prefix('lswdo')->name('lswdo.')->group(function () {
    Route::get('/eclip', [Lswdo\EclipCaseController::class, 'index'])->name('eclip.index');
    Route::get('/eclip/{eclipCase}', [Lswdo\EclipCaseController::class, 'show'])->name('eclip.show');
    Route::post('/eclip/{eclipCase}/eligibility', [Lswdo\EclipCaseController::class, 'decide'])->name('eclip.eligibility.decide');
    Route::post('/eclip/{eclipCase}/documents', [Mblrc\EclipDocumentController::class, 'store'])->name('eclip.documents.store');
    Route::get('/eclip-document-versions/{version}', EclipDocumentDownloadController::class)->name('eclip.documents.download');
    Route::get('/eclip-document-versions/{version}/preview', [Lswdo\EclipDocumentPreviewController::class, 'caseDocument'])->name('eclip.documents.preview');
    Route::get('/eclip/{eclipCase}/basic-services', [Lswdo\EclipBasicServiceController::class, 'index'])->name('eclip.basic-services.index');
    Route::post('/eclip/{eclipCase}/basic-services', [Lswdo\EclipBasicServiceController::class, 'store'])->name('eclip.basic-services.store');
    Route::put('/eclip-basic-services/{basicService}', [Lswdo\EclipBasicServiceController::class, 'update'])->name('eclip.basic-services.update');
    Route::post('/eclip-basic-services/{basicService}/documents', [EclipBasicServiceDocumentController::class, 'store'])->name('eclip.basic-services.documents.store');
    Route::get('/eclip-basic-service-documents/{document}', [EclipBasicServiceDocumentController::class, 'download'])->name('eclip.basic-services.documents.download');
    Route::get('/eclip-basic-service-documents/{document}/preview', [Lswdo\EclipDocumentPreviewController::class, 'basicServiceDocument'])->name('eclip.basic-services.documents.preview');
});

Route::middleware(['auth', 'role:japic'])->prefix('japic')->name('japic.')->group(function () {
    Route::get('/eclip', [Japic\EclipCaseController::class, 'index'])->name('eclip.index');
    Route::get('/eclip/{eclipCase}', [Japic\EclipCaseController::class, 'show'])->name('eclip.show');
    Route::post('/eclip-documents/{document}/review', [Japic\EclipCaseController::class, 'review'])->name('eclip.documents.review');
    Route::get('/eclip-document-versions/{version}', EclipDocumentDownloadController::class)->name('eclip.documents.download');
});

Route::middleware(['auth', 'role:lswdo,eclip_assessor'])->prefix('eclip-assessor')->name('eclip_assessor.')->group(function () {
    Route::get('/cases', [EclipAssessor\AssessmentController::class, 'index'])->name('cases.index');
    Route::get('/cases/{eclipCase}', [EclipAssessor\AssessmentController::class, 'show'])->name('cases.show');
    Route::post('/cases/{eclipCase}/assessment', [EclipAssessor\AssessmentController::class, 'store'])->name('assessments.store');
    Route::post('/cases/{eclipCase}/submit', [EclipAssessor\AssessmentController::class, 'submit'])->name('assessments.submit');
});

Route::middleware(['auth', 'role:dilg_provincial_focal,dilg_regional,nboo_eclip_pmo,dilg_reviewer'])->prefix('dilg-reviewer')->name('dilg_reviewer.')->group(function () {
    Route::get('/cases', [EclipReviewController::class, 'index'])->name('cases.index');
    Route::get('/cases/{eclipCase}', [EclipReviewController::class, 'show'])->name('cases.show');
    Route::post('/cases/{eclipCase}/decision', [EclipReviewController::class, 'decide'])->name('cases.decide');
});

Route::middleware(['auth', 'role:dilg_fms,eclip_funding_officer'])->prefix('eclip-funding')->name('eclip_funding.')->group(function () {
    Route::get('/cases', [FundingController::class, 'index'])->name('cases.index');
    Route::get('/cases/{eclipCase}', [FundingController::class, 'show'])->name('cases.show');
    Route::post('/cases/{eclipCase}/transactions', [FundingController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}/proof', [FundingController::class, 'download'])->name('transactions.proof');
});

Route::middleware(['auth', 'role:local_eclip_committee'])->prefix('local-eclip')->name('local_eclip.')->group(function () {
    Route::get('/cases', [ReleaseController::class, 'index'])->name('cases.index');
    Route::get('/cases/{eclipCase}', [ReleaseController::class, 'show'])->name('cases.show');
    Route::post('/cases/{eclipCase}/releases', [ReleaseController::class, 'store'])->name('releases.store');
    Route::get('/releases/{release}/acknowledgment', [ReleaseController::class, 'download'])->name('releases.acknowledgment');
});

Route::middleware(['auth', 'role:39th_ib'])->prefix('39th-ib')->name('ib39.')->group(function () {
    Route::get('/', [Ib39\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/areas', [Ib39\AreaController::class, 'index'])->name('areas.index');
    Route::put('/areas/{area}', [Ib39\AreaController::class, 'update'])->name('areas.update');
    Route::get('/map', [Ib39\AreaController::class, 'map'])->name('map');
    Route::get('/map-data', [Ib39\AreaController::class, 'mapData'])->name('map.data');
});

Route::middleware(['auth', 'role:afp'])->prefix('afp')->name('afp.')->group(function () {
    Route::get('/', [Afp\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rcsp', [Afp\RcspController::class, 'index'])->name('rcsp.index');
});

Route::middleware(['auth', 'role:pnp'])->prefix('pnp')->name('pnp.')->group(function () {
    Route::get('/', [Pnp\DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/auth.php';
